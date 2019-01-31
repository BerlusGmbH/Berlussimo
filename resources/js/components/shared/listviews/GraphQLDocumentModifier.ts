import {DefinitionNode, DirectiveNode, TypeNode, visit} from "graphql";
import _ from "lodash";

export default class GraphQLDocumentModifier {

    static readonly QUERY_ROOT = 'Query';

    public static addFragments(query, fragments) {
        const fragmentDefinitions: {
            [name: string]: any
        } = {};
        const fragmentSpreads: Set<string> = new Set<string>();
        visit(fragments, {
            FragmentDefinition(node, _key, _parent, _path, _ancestors) {
                fragmentDefinitions[node.name.value] = node;
            }
        });
        return GraphQLDocumentModifier.fragmentSpreadTransitiveHull(query, fragmentSpreads, fragmentDefinitions);
    }

    public static removeClientOnlyDirectives(query) {
        const clientOnlyDirectives = ['label', 'variables'];
        return visit(query, {
            Field(node, _key, _parent, _path, _ancestors) {
                let modified = false;
                if (node.directives && node.directives.length > 0) {
                    const nodeCopy = _.cloneDeep(node);
                    if (nodeCopy.directives) {
                        (nodeCopy.directives as DirectiveNode[]) = nodeCopy.directives.filter(directive => {
                            if (clientOnlyDirectives.includes(directive.name.value)) {
                                modified = true;
                                return true;
                            }
                            return false
                        });
                    }
                    if (modified) {
                        return nodeCopy;
                    }
                }
                return undefined;
            }
        });
    }

    public static getHeaders(query) {
        const rows: {
            text: string,
            value: string,
            type: string
        }[] = [];
        visit(query, {
            Field(node, _key, _parent, _path, ancestors) {
                const {rootName, fields} = GraphQLDocumentModifier.extractRootTypeAndFields(node, ancestors);
                if (rootName !== 'Query') {
                    return
                }
                if (GraphQLDocumentModifier.isScalar(node)) {
                    let name = fields.length > 0 ? fields[fields.length - 1] : '';
                    if (name === 'data' && fields.length === 2) {
                        name = fields[0];
                    }
                    if (node.directives && node.directives.length > 0) {
                        const label = GraphQLDocumentModifier.getLabelText(node.directives);
                        name = label ? label : name;
                    }
                    if (fields.length > 1 && fields[1] === 'data') {
                        fields.splice(1, 1);
                    }
                    rows.push({
                        text: name,
                        value: fields.join('.'),
                        type: 'ScalarType'
                    });
                }
            },
            FragmentSpread(node, _key, _parent, _path, ancestors) {
                const {fields} = GraphQLDocumentModifier.extractRootTypeAndFields(node, ancestors);
                if (node.name.value.endsWith('Identifier')) {
                    let name = fields.length > 0 ? fields[fields.length - 1] : '';
                    if (name === 'data' && fields.length === 2) {
                        name = fields[0];
                    }
                    if (node.directives && node.directives.length > 0) {
                        const label = GraphQLDocumentModifier.getLabelText(node.directives);
                        name = label ? label : name;
                    }
                    if (fields.length > 1 && fields[1] === 'data') {
                        fields.splice(1, 1);
                    }
                    fields.splice(fields.length - 1, 1);
                    rows.push({
                        text: name,
                        value: fields.join('.'),
                        type: 'ObjectType'
                    });
                }
            }
        });
        return rows;
    }

    public static getVariables(query) {
        const variables: any[] = [];
        visit(query, {
            OperationDefinition(node, _key, _parent, _path, _ancestors) {
                if (!node.variableDefinitions) {
                    return;
                }
                for (let variableDefinition of node.variableDefinitions) {
                    if (!['first', 'page', 'search'].includes(variableDefinition.variable.name.value)) {
                        let variable = {
                            label: variableDefinition.variable.name.value,
                            name: variableDefinition.variable.name.value,
                            kind: GraphQLDocumentModifier.getVariableType(variableDefinition.type),
                            list: GraphQLDocumentModifier.isListType(variableDefinition)
                        };
                        GraphQLDocumentModifier.fillVariableWithVariableDirective(node, variable);
                        variables.push(variable);
                    }
                }
            }
        });
        return variables;
    }

    protected static fragmentSpreadTransitiveHull(query, previousFragmentSpreads: Set<string>, fragmentDefinitions) {
        const currentFragmentSpreads: Set<string> = new Set<string>();
        let difference: Set<string> = new Set<string>();
        query = visit(query, {
            FragmentSpread(node, _key, _parent, _path, _ancestors) {
                if (fragmentDefinitions[node.name.value]) {
                    currentFragmentSpreads.add(node.name.value);
                }
            },
            Document: {
                leave(node, _key, _parent, _path, _ancestors) {
                    difference = new Set<string>([...currentFragmentSpreads].filter(x => !previousFragmentSpreads.has(x)));
                    if (difference.size > 0) {
                        const nodeCopy = _.cloneDeep(node);
                        for (let name of difference) {
                            (nodeCopy.definitions as DefinitionNode[]).push(fragmentDefinitions[name]);
                        }
                        return nodeCopy;
                    }
                    return undefined;
                }
            }
        });
        if (difference.size > 0) {
            const union: Set<string> = new Set<string>([...previousFragmentSpreads, ...currentFragmentSpreads]);
            return GraphQLDocumentModifier.fragmentSpreadTransitiveHull(query, union, fragmentDefinitions);
        }
        return query;
    }

    protected static isListType(definition) {
        return definition.type.kind === "ListType" || (definition.type.type && definition.type.type.kind === "ListType");
    }

    protected static extractRootTypeAndFields(leaf, ancestors): { rootType: string, rootName: string, fields: any[] } {
        let rootType, rootName;
        let fields: any = [];
        const leafName = leaf.alias ? leaf.alias.value : leaf.name.value;
        fields.unshift(leafName);
        for (let i = ancestors.length - 2; i >= 0; i -= 3) {
            if (GraphQLDocumentModifier.isFragmentDefinition(ancestors[i])) {
                rootType = GraphQLDocumentModifier.getNodeTypeFromFragmentDefinition(ancestors[i]);
                rootName = GraphQLDocumentModifier.getNodeName(ancestors[i]);
                break;
            } else if (GraphQLDocumentModifier.isQueryOperationDefinition(ancestors[i])) {
                rootType = GraphQLDocumentModifier.QUERY_ROOT;
                rootName = GraphQLDocumentModifier.QUERY_ROOT;
                break;
            }
            const name = ancestors[i].alias ? ancestors[i].alias.value : ancestors[i].name.value;
            fields.unshift(name);
        }
        return {rootType, rootName, fields};
    }

    protected static fillVariableWithVariableDirective(node, variable) {
        for (let directive of node.directives) {
            if (directive.name.value === "variables") {
                if (directive.arguments.length === 1) {
                    const variablesArgument = directive.arguments[0];
                    const variableDirectiveDefinition = GraphQLDocumentModifier.getVariableDirectiveDefinitionFromVariablesArgument(variablesArgument, variable);
                    if (variableDirectiveDefinition) {
                        for (let definition of variableDirectiveDefinition.fields) {
                            if (!["name"].includes(definition.name.value)) {
                                if (definition.value.kind === "ListValue") {
                                    variable[definition.name.value] = definition.value.values.map(item => item.value);
                                } else {
                                    variable[definition.name.value] = definition.value.value;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected static isScalar(node) {
        return !node.selectionSet;
    }

    protected static isFragmentDefinition(node) {
        return node.kind === 'FragmentDefinition';
    }

    protected static isQueryOperationDefinition(node) {
        return node.kind === 'OperationDefinition' && node.operation === 'query';
    }

    protected static getVariableDirectiveDefinitionFromVariablesArgument(argument, variable) {
        for (let value of argument.value.values) {
            for (let field of value.fields) {
                if (field.name.value === "name" && field.value.value === variable.name) {
                    return value;
                }
            }
        }
        return null;
    }

    protected static getNodeTypeFromFragmentDefinition(node) {
        return node.typeCondition.name.value;
    }

    protected static getNodeName(node) {
        return node.name.value;
    }

    protected static getVariableType(node: TypeNode): string {
        if (node.kind === "NamedType") {
            return node.name.value;
        }
        return GraphQLDocumentModifier.getVariableType(node.type);
    }

    protected static getLabelText(directives) {
        for (let directive of directives) {
            if (directive.name.value === "label") {
                for (let argument of directive.arguments) {
                    if (argument.name.value === "text") {
                        return argument.value.value;
                    }
                }
            }
        }
        return null;
    }
}
